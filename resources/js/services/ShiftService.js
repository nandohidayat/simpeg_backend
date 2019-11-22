import client from "./client";

export default {
    getShifts() {
        return client.get("/api/shift");
    },
    postShift(shift) {
        return client.post("/api/shift", shift);
    },
    putShift(shift) {
        return client.put(`/api/shift/${shift.id_shift}`, shift);
    },
    deleteShift(id) {
        return client.delete(`/api/shift/${id}`);
    }
};
